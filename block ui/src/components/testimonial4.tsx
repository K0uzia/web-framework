import { Avatar, AvatarImage } from "@/components/ui/avatar";
import { Card, CardContent, CardFooter } from "@/components/ui/card";
import { cn } from "@/lib/utils";

interface Testimonial4Props {
  className?: string;
}

const Testimonial4 = ({ className }: Testimonial4Props) => {
  return (
    <section className={cn("py-32", className)}>
      <div className="container">
        <div className="flex flex-col gap-6">
          <div className="grid grid-cols-1 items-stretch gap-x-0 gap-y-4 lg:grid-cols-3 lg:gap-4">
            <img
              src="https://deifkwefumgah.cloudfront.net/shadcnblocks/block/placeholder-1.svg"
              alt="placeholder"
              className="h-72 w-full rounded-md object-cover lg:h-auto"
            />
            <Card className="col-span-2 flex items-center justify-center p-6">
              <div className="flex flex-col gap-4">
                <q className="text-xl font-medium lg:text-3xl">
                  Lorem ipsum dolor sit amet consectetur adipisicing elit. Atque
                  eveniet suscipit corporis sequi usdam alias fugiat iusto
                  perspiciatis.
                </q>
                <div className="flex flex-col items-start">
                  <p>John Doe</p>
                  <p className="text-muted-foreground">CEO, Company Name</p>
                </div>
              </div>
            </Card>
          </div>
          <div className="grid grid-cols-1 gap-4 lg:grid-cols-3">
            <Card>
              <CardContent className="px-6 pt-6 leading-7 text-foreground/70">
                <q>
                  Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                  Ipsa, eveniet inventore! Omnis incidunt vel iste.
                </q>
              </CardContent>
              <CardFooter>
                <div className="flex gap-4 leading-5">
                  <Avatar className="size-9 rounded-full ring-1 ring-input">
                    <AvatarImage
                      src="https://deifkwefumgah.cloudfront.net/shadcnblocks/block/avatar-1.webp"
                      alt="placeholder"
                    />
                  </Avatar>
                  <div className="text-sm">
                    <p className="font-medium">John Doe</p>
                    <p className="text-muted-foreground">CEO, Company Name</p>
                  </div>
                </div>
              </CardFooter>
            </Card>
            <Card>
              <CardContent className="px-6 pt-6 leading-7 text-foreground/70">
                <q>
                  Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                  Ipsa, eveniet inventore! Omnis incidunt vel iste.
                </q>
              </CardContent>
              <CardFooter>
                <div className="flex gap-4 leading-5">
                  <Avatar className="size-9 rounded-full ring-1 ring-input">
                    <AvatarImage
                      src="https://deifkwefumgah.cloudfront.net/shadcnblocks/block/avatar-1.webp"
                      alt="placeholder"
                    />
                  </Avatar>
                  <div className="text-sm">
                    <p className="font-medium">John Doe</p>
                    <p className="text-muted-foreground">CEO, Company Name</p>
                  </div>
                </div>
              </CardFooter>
            </Card>
            <Card>
              <CardContent className="px-6 pt-6 leading-7 text-foreground/70">
                <q>
                  Lorem ipsum dolor, sit amet consectetur adipisicing elit.
                  Ipsa, eveniet inventore! Omnis incidunt vel iste.
                </q>
              </CardContent>
              <CardFooter>
                <div className="flex gap-4 leading-5">
                  <Avatar className="size-9 rounded-full ring-1 ring-input">
                    <AvatarImage
                      src="https://deifkwefumgah.cloudfront.net/shadcnblocks/block/avatar-1.webp"
                      alt="placeholder"
                    />
                  </Avatar>
                  <div className="text-sm">
                    <p className="font-medium">John Doe</p>
                    <p className="text-muted-foreground">CEO, Company Name</p>
                  </div>
                </div>
              </CardFooter>
            </Card>
          </div>
        </div>
      </div>
    </section>
  );
};

export { Testimonial4 };
